from odoo import fields, models

class Position(models.Model):
    _name = 'qcv.position'
    _description = 'Imported position'
    _order = 'title'

    source_id = fields.Integer(index=True)
    company = fields.Char()
    title = fields.Char(required=True)
    description = fields.Char()
    level = fields.Char()
    maxProjects = fields.Integer()
    cv_count = fields.Integer()
    imported_at = fields.Datetime()
    created_at = fields.Datetime()
    tags = fields.Char()
    attribute_ids = fields.One2many('qcv.position.attribute', 'position_id')

class PositionAttribute(models.Model):
    _name = 'qcv.position.attribute'
    _description = 'Position attribute with aggregates'
    _order = 'id'

    position_id = fields.Many2one('qcv.position', required=True, ondelete='cascade')
    name = fields.Char(required=True)
    data_type = fields.Char(string='Type')
    answers_count = fields.Integer()
    avg_value = fields.Float()
    min_value = fields.Float()
    max_value = fields.Float()
    top_values = fields.Char()

